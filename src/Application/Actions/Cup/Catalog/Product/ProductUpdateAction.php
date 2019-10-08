<?php

namespace App\Application\Actions\Cup\Catalog\Product;

use App\Application\Actions\Cup\Catalog\CatalogAction;
use Exception;

class ProductUpdateAction extends CatalogAction
{
    protected function action(): \Slim\Http\Response
    {
        if ($this->resolveArg('product') && \Ramsey\Uuid\Uuid::isValid($this->resolveArg('product'))) {
            /** @var \App\Domain\Entities\Catalog\Product $product */
            $product = $this->productRepository->findOneBy(['uuid' => $this->resolveArg('product'), 'status' => \App\Domain\Types\Catalog\ProductStatusType::STATUS_WORK]);

            if (!$product->isEmpty()) {
                if ($this->request->isPost()) {
                    // remove uploaded image
                    if (($uuidFile = $this->request->getParam('delete-image')) !== null && \Ramsey\Uuid\Uuid::isValid($uuidFile)) {
                        /** @var \App\Domain\Entities\File $file */
                        $file = $this->fileRepository->findOneBy(['uuid' => $uuidFile]);

                        if ($file) {
                            try {
                                $file->unlink();
                                $this->entityManager->remove($file);
                                $this->entityManager->flush();
                            } catch (Exception $e) {
                                // todo nothing
                            }
                        }
                    } else {
                        $data = [
                            'uuid' => $product->uuid,
                            'category' => $this->request->getParam('category'),
                            'title' => $this->request->getParam('title'),
                            'description' => $this->request->getParam('description'),
                            'extra' => $this->request->getParam('extra'),
                            'address' => $this->request->getParam('address'),
                            'vendorcode' => $this->request->getParam('vendorcode'),
                            'barcode' => $this->request->getParam('barcode'),
                            'priceFirst' => $this->request->getParam('priceFirst'),
                            'price' => $this->request->getParam('price'),
                            'priceWholesale' => $this->request->getParam('priceWholesale'),
                            'volume' => $this->request->getParam('volume'),
                            'unit' => $this->request->getParam('unit'),
                            'stock' => $this->request->getParam('stock'),
                            'field1' => $this->request->getParam('field1'),
                            'field2' => $this->request->getParam('field2'),
                            'field3' => $this->request->getParam('field3'),
                            'field4' => $this->request->getParam('field4'),
                            'field5' => $this->request->getParam('field5'),
                            'country' => $this->request->getParam('country'),
                            'manufacturer' => $this->request->getParam('manufacturer'),
                            'tags' => $this->request->getParam('tags'),
                            'order' => $this->request->getParam('order'),
                            'date' => $this->request->getParam('date'),
                            'external_id' => $this->request->getParam('external_id'),
                        ];

                        $check = \App\Domain\Filters\Catalog\Product::check($data);

                        if ($check === true) {
                            try {
                                $product->replace($data);
                                $this->entityManager->persist($product);
                                $this->handlerFileUpload(\App\Domain\Types\FileItemType::ITEM_CATALOG_PRODUCT, $product->uuid);
                                $this->entityManager->flush();

                                if ($this->request->getParam('save', 'exit') === 'exit') {
                                    return $this->response->withAddedHeader('Location', '/cup/catalog/product/' . $product->category)->withStatus(301);
                                }
                            } catch (Exception $e) {
                                // todo nothing
                            }
                        }
                    }
                }

                $categories = collect($this->categoryRepository->findAll());

                $files = collect($this->fileRepository->findBy([
                    'item' => \App\Domain\Types\FileItemType::ITEM_CATALOG_PRODUCT,
                    'item_uuid' => $product->uuid,
                ]));

                return $this->respondRender('cup/catalog/product/form.twig', [
                    'category' => $categories->firstWhere('uuid', $product->category),
                    'categories' => $categories,
                    'measure' => $this->getMeasure(),
                    'files' => $files,
                    'item' => $product,
                ]);
            }

            return $this->response->withAddedHeader('Location', '/cup/catalog/product/' . $product->category)->withStatus(301);
        }

        return $this->response->withAddedHeader('Location', '/cup/catalog')->withStatus(301);
    }
}
